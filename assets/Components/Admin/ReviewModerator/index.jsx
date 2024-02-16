import React, { useEffect, useState } from 'react';
import { Button } from '../../../UI/Button/Button';
import { useOpenState } from '../../../CustomHook/useOpenState';
import { SiteConfig } from '../../../Config/SiteConfig';
import { useFetchOut } from '../../../CustomHook/fetch/useFetchOut';

export const ReviewModerator = ({id, initialStatusLabel}) => {

    const [status, setStatus] = useState(null);

    const [updating, openUpdate, closeUpdate] = useOpenState();

    useEffect(() => {
        if(initialStatusLabel === 'En_attente') {
            openUpdate();
        }
        setStatus(initialStatusLabel.split('_').join(' '));
    }, [initialStatusLabel])

    const {sendData, loading, info} = useFetchOut('/admin/api/review/'+id+'/updateModerationStatus');

    const handleOpenUpdate = async () => {
        sendData(null, (fetchResult) => {
            setStatus(SiteConfig.MODERATION_STATUS_PENDING_LABEL);
            openUpdate();
        });
    }

    const handleAccept = async () => {
        if(!confirm('Voulez-vous vraiment publier l\'avis ?')) {
            return;
        }
        sendData({status: SiteConfig.MODERATION_STATUS_ACCEPTED}, (fetchResult) => {
            setStatus(SiteConfig.MODERATION_STATUS_ACCEPTED_LABEL);
            closeUpdate();
        });
    }

    const handleRefuse = async () => {
        sendData({status: SiteConfig.MODERATION_STATUS_REFUSED}, (fetchResult) => {
            setStatus(SiteConfig.MODERATION_STATUS_REFUSED_LABEL);
            closeUpdate();
        });
    }

    return (
        <>
            <div className="admin-form-field-with-button">
                <span className="moderationStatus">{status}</span>
                {
                    !updating && (
                        <Button additionalClass="admin-button admin-small-button" onClick={handleOpenUpdate}>Modifier</Button>
                    )
                }
            </div>
            {
                    info && <div className="admin-form-info">{info}</div>
            }
            {
                    loading && <div className="admin-form-info">Chargement...</div>
            }
            {
            updating && (
                    <div className="admin-buttons-wrapper">
                        <Button additionalClass={'admin-button' + (loading ? ' disabled': '')} onClick={handleAccept} disabled={loading === true}>Accepter</Button>
                        <Button additionalClass={'admin-button secondary-color' + (loading ? ' disabled': '')} onClick={handleRefuse} disabled={loading === true}>Refuser</Button>
                    </div>
                )
            }
        </>
        
    )
}